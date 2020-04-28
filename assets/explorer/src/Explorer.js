import React, { useState, useRef } from 'react';
import GraphiQL from 'graphiql';
import GraphiQLExplorer from 'graphiql-explorer';

const Explorer = ({
  fetcher, schema, query: q, variables,
}) => {
  const graphiQLEl = useRef(null);
  const [explorerIsOpen, setExplorerIsOpen] = useState(false);
  const [query, setQuery] = useState(q);

  return (
    <div className="graphiql-container">
      <GraphiQLExplorer
        schema={schema}
        query={query}
        onEdit={setQuery}
        onRunOperation={operationName =>
          graphiQLEl.current.handleRunQuery(operationName)
        }
        explorerIsOpen={explorerIsOpen}
        onToggleExplorer={() => setExplorerIsOpen(!explorerIsOpen)}
      />
      <GraphiQL
        ref={graphiQLEl}
        fetcher={fetcher}
        schema={schema}
        query={query}
        variables={variables}
        onEditQuery={setQuery}
      >
        <GraphiQL.Toolbar>
          <GraphiQL.Button
            onClick={() => graphiQLEl.current.handlePrettifyQuery()}
            label="Prettify"
            title="Prettify Query (Shift-Ctrl-P)"
          />
          <GraphiQL.Button
            onClick={() => graphiQLEl.current.handleToggleHistory()}
            label="History"
            title="Show History"
          />
          <GraphiQL.Button
            onClick={() => setExplorerIsOpen(!explorerIsOpen)}
            label="Explorer"
            title="Toggle Explorer"
          />
        </GraphiQL.Toolbar>
      </GraphiQL>
    </div>
  );
};

export default Explorer;
